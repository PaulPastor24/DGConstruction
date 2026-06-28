'use client'

import { makeStyles } from '@fluentui/react-components'

const useStyles = makeStyles({
  root: {
    display: 'grid',
    gridTemplateColumns: 'repeat(5, 1fr)',
    gap: '20px',
    '@media (max-width: 1400px)': {
      gridTemplateColumns: 'repeat(3, 1fr)',
    },
    '@media (max-width: 768px)': {
      gridTemplateColumns: 'repeat(2, 1fr)',
    },
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: '16px',
    padding: '24px',
    border: '1px solid rgba(0, 0, 0, 0.08)',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
    transition: 'all 0.3s ease',
    display: 'flex',
    flexDirection: 'column',
    gap: '12px',
    '&:hover': {
      boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)',
      transform: 'translateY(-2px)',
    },
  },
  icon: {
    fontSize: '32px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
  },
  label: {
    fontSize: '13px',
    color: '#999999',
    fontWeight: 600,
    textTransform: 'uppercase',
    letterSpacing: '0.5px',
  },
  value: {
    fontSize: '32px',
    fontWeight: 700,
    color: '#2F6B3C',
    lineHeight: '1.2',
  },
  description: {
    fontSize: '13px',
    color: '#666666',
    fontWeight: 400,
  },
})

export function AttendanceSummaryCards() {
  const styles = useStyles()

  const cards = [
    {
      icon: '👥',
      label: 'Present Workers',
      value: '48',
      description: 'On site and verified',
    },
    {
      icon: '❌',
      label: 'Absent Workers',
      value: '2',
      description: 'Not marked present',
    },
    {
      icon: '⏱️',
      label: 'Late Arrivals',
      value: '1',
      description: 'Arrived after 8:00 AM',
    },
    {
      icon: '⊘',
      label: 'Pending Biometric',
      value: '0',
      description: 'Awaiting verification',
    },
    {
      icon: '✓',
      label: 'Success Rate',
      value: '100%',
      description: 'Biometric verification',
    },
  ]

  return (
    <div className={styles.root}>
      {cards.map((card, idx) => (
        <div key={idx} className={styles.card}>
          <div className={styles.icon}>{card.icon}</div>
          <div className={styles.label}>{card.label}</div>
          <div className={styles.value}>{card.value}</div>
          <div className={styles.description}>{card.description}</div>
        </div>
      ))}
    </div>
  )
}
