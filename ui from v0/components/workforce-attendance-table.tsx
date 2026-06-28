'use client'

import { useState } from 'react'
import { makeStyles } from '@fluentui/react-components'

const useStyles = makeStyles({
  root: {
    backgroundColor: '#FFFFFF',
    borderRadius: '16px',
    padding: '32px',
    border: '1px solid rgba(0, 0, 0, 0.08)',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
  },
  header: {
    marginBottom: '28px',
    paddingBottom: '20px',
    borderBottom: '1px solid rgba(0, 0, 0, 0.08)',
  },
  title: {
    fontSize: '20px',
    fontWeight: 700,
    color: '#2F6B3C',
    marginBottom: '6px',
  },
  subtitle: {
    fontSize: '14px',
    color: '#999999',
    fontWeight: 400,
  },
  controls: {
    display: 'flex',
    gap: '16px',
    marginBottom: '24px',
    flexWrap: 'wrap',
  },
  searchInput: {
    flex: 1,
    minWidth: '200px',
    padding: '12px 16px',
    border: '1px solid rgba(0, 0, 0, 0.1)',
    borderRadius: '10px',
    fontSize: '14px',
    '&:focus': {
      outline: 'none',
      border: '1px solid #2F6B3C',
      boxShadow: '0 0 0 3px rgba(47, 107, 60, 0.1)',
    },
  },
  filterButton: {
    padding: '12px 16px',
    backgroundColor: '#F5F5F0',
    border: '1px solid rgba(0, 0, 0, 0.1)',
    borderRadius: '10px',
    cursor: 'pointer',
    fontSize: '14px',
    fontWeight: 500,
    color: '#333333',
    transition: 'all 0.2s ease',
    '&:hover': {
      backgroundColor: '#EEEEEA',
    },
  },
  tableWrapper: {
    overflowX: 'auto',
  },
  table: {
    width: '100%',
    borderCollapse: 'collapse',
  },
  th: {
    padding: '14px 16px',
    textAlign: 'left',
    fontSize: '12px',
    fontWeight: 700,
    color: '#2F6B3C',
    textTransform: 'uppercase',
    letterSpacing: '0.5px',
    backgroundColor: 'rgba(47, 107, 60, 0.04)',
    borderBottom: '1px solid rgba(0, 0, 0, 0.08)',
  },
  td: {
    padding: '14px 16px',
    fontSize: '14px',
    color: '#333333',
    borderBottom: '1px solid rgba(0, 0, 0, 0.06)',
  },
  tr: {
    '&:hover': {
      backgroundColor: 'rgba(0, 0, 0, 0.02)',
    },
  },
  badge: {
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    padding: '6px 12px',
    borderRadius: '20px',
    fontSize: '12px',
    fontWeight: 700,
  },
  badgeGreen: {
    backgroundColor: 'rgba(102, 187, 106, 0.15)',
    color: '#2F6B3C',
  },
  badgeRed: {
    backgroundColor: 'rgba(255, 0, 0, 0.1)',
    color: '#CC0000',
  },
  badgeOrange: {
    backgroundColor: 'rgba(255, 153, 0, 0.15)',
    color: '#996600',
  },
  badgeGray: {
    backgroundColor: 'rgba(0, 0, 0, 0.1)',
    color: '#666666',
  },
})

export function WorkforceAttendanceTable() {
  const styles = useStyles()
  const [searchTerm, setSearchTerm] = useState('')

  const workers = [
    { id: 1, name: 'Michael Johnson', trade: 'Electrician', timeIn: '07:45', timeOut: '−', status: 'Present', biometric: 'Verified' },
    { id: 2, name: 'James Smith', trade: 'Carpenter', timeIn: '08:15', timeOut: '−', status: 'Late', biometric: 'Verified' },
    { id: 3, name: 'Robert Brown', trade: 'Concrete Worker', timeIn: '07:50', timeOut: '−', status: 'Present', biometric: 'Verified' },
    { id: 4, name: 'David Miller', trade: 'Foreman', timeIn: '07:30', timeOut: '−', status: 'Present', biometric: 'Verified' },
    { id: 5, name: 'William Davis', trade: 'Plumber', timeIn: '−', timeOut: '−', status: 'Absent', biometric: 'Pending' },
    { id: 6, name: 'Richard Wilson', trade: 'Welder', timeIn: '07:55', timeOut: '−', status: 'Present', biometric: 'Verified' },
  ]

  const getStatusBadge = (status: string) => {
    const badgeClass = 
      status === 'Present' ? styles.badgeGreen :
      status === 'Late' ? styles.badgeOrange :
      status === 'Absent' ? styles.badgeRed :
      styles.badgeGray
    
    return <span className={`${styles.badge} ${badgeClass}`}>{status}</span>
  }

  const getBiometricBadge = (status: string) => {
    const badgeClass = 
      status === 'Verified' ? styles.badgeGreen :
      status === 'Pending' ? styles.badgeOrange :
      styles.badgeRed
    
    return <span className={`${styles.badge} ${badgeClass}`}>{status}</span>
  }

  return (
    <div className={styles.root}>
      <div className={styles.header}>
        <div className={styles.title}>Workforce Attendance Table</div>
        <div className={styles.subtitle}>Track worker attendance, biometric verification, and time records.</div>
      </div>

      <div className={styles.controls}>
        <input
          type="text"
          placeholder="Search worker name..."
          className={styles.searchInput}
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
        />
        <button className={styles.filterButton}>Filter by Trade</button>
        <button className={styles.filterButton}>Filter by Status</button>
        <button className={styles.filterButton}>Filter by Biometric</button>
      </div>

      <div className={styles.tableWrapper}>
        <table className={styles.table}>
          <thead>
            <tr>
              <th className={styles.th}>Worker Name</th>
              <th className={styles.th}>Trade</th>
              <th className={styles.th}>Time In</th>
              <th className={styles.th}>Time Out</th>
              <th className={styles.th}>Attendance Status</th>
              <th className={styles.th}>Biometric Status</th>
            </tr>
          </thead>
          <tbody>
            {workers.map((worker) => (
              <tr key={worker.id} className={styles.tr}>
                <td className={styles.td}>{worker.name}</td>
                <td className={styles.td}>{worker.trade}</td>
                <td className={styles.td}>{worker.timeIn}</td>
                <td className={styles.td}>{worker.timeOut}</td>
                <td className={styles.td}>{getStatusBadge(worker.status)}</td>
                <td className={styles.td}>{getBiometricBadge(worker.biometric)}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
