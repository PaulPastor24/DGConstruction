'use client'

import { makeStyles } from '@fluentui/react-components'

const useStyles = makeStyles({
  root: {
    backgroundColor: '#FFFFFF',
    borderRadius: '16px',
    padding: '32px',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
    border: '1px solid rgba(0, 0, 0, 0.08)',
  },
  header: {
    marginBottom: '28px',
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
  table: {
    width: '100%',
    borderCollapse: 'collapse',
  },
  tableHeader: {
    display: 'grid',
    gridTemplateColumns: '2fr 1.2fr 1fr 1fr 1.2fr',
    gap: '16px',
    paddingTop: '12px',
    paddingBottom: '12px',
    paddingLeft: '16px',
    paddingRight: '16px',
    borderBottom: '1px solid rgba(0, 0, 0, 0.08)',
    backgroundColor: 'rgba(47, 107, 60, 0.04)',
    borderRadius: '10px',
    marginBottom: '8px',
  },
  headerCell: {
    fontSize: '12px',
    fontWeight: 700,
    color: '#2F6B3C',
    textTransform: 'uppercase',
    letterSpacing: '0.5px',
  },
  tableRow: {
    display: 'grid',
    gridTemplateColumns: '2fr 1.2fr 1fr 1fr 1.2fr',
    gap: '16px',
    paddingTop: '14px',
    paddingBottom: '14px',
    paddingLeft: '16px',
    paddingRight: '16px',
    borderBottom: '1px solid rgba(0, 0, 0, 0.06)',
    borderRadius: '10px',
    transition: 'all 0.2s ease',
    alignItems: 'center',
    '&:hover': {
      backgroundColor: 'rgba(0, 0, 0, 0.02)',
    },
    '&:last-child': {
      borderBottom: 'none',
    },
  },
  cell: {
    fontSize: '15px',
    color: '#333333',
    fontWeight: 500,
  },
  milestoneName: {
    fontWeight: 700,
  },
  date: {
    fontSize: '14px',
    color: '#666666',
    fontWeight: 600,
  },
  daysRemaining: {
    fontSize: '14px',
    fontWeight: 700,
    color: '#2F6B3C',
  },
  daysRemainingSoon: {
    color: '#CC6600',
  },
  badge: {
    display: 'inline-block',
    paddingTop: '6px',
    paddingBottom: '6px',
    paddingLeft: '12px',
    paddingRight: '12px',
    borderRadius: '16px',
    fontSize: '12px',
    fontWeight: 700,
    textTransform: 'uppercase',
    letterSpacing: '0.3px',
  },
  badgeOnTrack: {
    backgroundColor: 'rgba(102, 187, 106, 0.15)',
    color: '#2F6B3C',
  },
  badgeWarning: {
    backgroundColor: 'rgba(255, 238, 88, 0.2)',
    color: '#CC6600',
  },
  badgeCompleted: {
    backgroundColor: 'rgba(102, 187, 106, 0.15)',
    color: '#2F6B3C',
  },
  footer: {
    display: 'flex',
    justifyContent: 'center',
    marginTop: '32px',
  },
  button: {
    paddingTop: '12px',
    paddingBottom: '12px',
    paddingLeft: '32px',
    paddingRight: '32px',
    borderRadius: '10px',
    border: '1.5px solid #2F6B3C',
    backgroundColor: 'transparent',
    color: '#2F6B3C',
    cursor: 'pointer',
    fontSize: '15px',
    fontWeight: 700,
    transition: 'all 0.3s ease',
    height: '48px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    '&:hover': {
      backgroundColor: 'rgba(47, 107, 60, 0.08)',
      boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)',
      transform: 'translateY(-2px)',
    },
  },
})

interface Milestone {
  id: string
  name: string
  targetDate: string
  daysRemaining: number
  priority: 'high' | 'medium' | 'low'
  status: 'on-track' | 'warning' | 'completed'
}

const milestones: Milestone[] = [
  {
    id: '1',
    name: 'Roof Framing Complete',
    targetDate: 'Jun 28, 2024',
    daysRemaining: 2,
    priority: 'high',
    status: 'warning',
  },
  {
    id: '2',
    name: 'Roofing Materials Installed',
    targetDate: 'Jul 5, 2024',
    daysRemaining: 9,
    priority: 'high',
    status: 'on-track',
  },
  {
    id: '3',
    name: 'Final Roof Inspection',
    targetDate: 'Jul 15, 2024',
    daysRemaining: 19,
    priority: 'medium',
    status: 'on-track',
  },
  {
    id: '4',
    name: 'Finishing Phase Begins',
    targetDate: 'Jul 20, 2024',
    daysRemaining: 24,
    priority: 'medium',
    status: 'on-track',
  },
  {
    id: '5',
    name: 'Project Completion',
    targetDate: 'Aug 20, 2024',
    daysRemaining: 55,
    priority: 'medium',
    status: 'on-track',
  },
]

export function UpcomingMilestonesTable() {
  const styles = useStyles()

  return (
    <div className={styles.root}>
      <div className={styles.header}>
        <div className={styles.title}>Upcoming Milestones</div>
        <div className={styles.subtitle}>Next five critical milestones</div>
      </div>

      <div className={styles.tableHeader}>
        <div className={styles.headerCell}>Milestone</div>
        <div className={styles.headerCell}>Target Date</div>
        <div className={styles.headerCell}>Days Left</div>
        <div className={styles.headerCell}>Priority</div>
        <div className={styles.headerCell}>Status</div>
      </div>

      {milestones.map((milestone) => (
        <div key={milestone.id} className={styles.tableRow}>
          <div className={`${styles.cell} ${styles.milestoneName}`}>{milestone.name}</div>
          <div className={`${styles.cell} ${styles.date}`}>{milestone.targetDate}</div>
          <div className={`${styles.cell} ${styles.daysRemaining} ${milestone.daysRemaining < 5 ? styles.daysRemainingSoon : ''}`}>{milestone.daysRemaining} days</div>
          <div className={styles.cell}>
            <span
              className={styles.badge}
              style={{
                backgroundColor: milestone.priority === 'high' ? 'rgba(255, 238, 88, 0.2)' : milestone.priority === 'medium' ? 'rgba(212, 225, 87, 0.15)' : 'rgba(102, 187, 106, 0.15)',
                color: milestone.priority === 'high' ? '#CC6600' : milestone.priority === 'medium' ? '#996600' : '#2F6B3C',
              }}
            >
              {milestone.priority === 'high' ? 'High' : milestone.priority === 'medium' ? 'Medium' : 'Low'}
            </span>
          </div>
          <div className={styles.cell}>
            <span
              className={`${styles.badge} ${milestone.status === 'on-track' ? styles.badgeOnTrack : milestone.status === 'warning' ? styles.badgeWarning : styles.badgeCompleted}`}
            >
              {milestone.status === 'on-track' ? 'On Track' : milestone.status === 'warning' ? 'At Risk' : 'Completed'}
            </span>
          </div>
        </div>
      ))}

      <div className={styles.footer}>
        <button className={styles.button}>View Full Timeline</button>
      </div>
    </div>
  )
}
